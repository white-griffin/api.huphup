<?php

namespace App\Services\Wallet;

use App\Enums\WalletTransactionType;
use App\Models\Payment;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class WalletService
{

    public function creditPending(
        Wallet $wallet,
        int $amount,
        WalletTransactionType $type,
        ?Payment $payment = null,
        ?string $description = null,
    ): void
    {
        DB::transaction(function () use (
            $wallet,
            $amount,
            $type,
            $payment,
            $description
        ) {

            $wallet = $this->lockWallet($wallet);

            $availableBefore = $wallet->available_balance;
            $pendingBefore = $wallet->pending_balance;

            $pendingAfter = $pendingBefore + $amount;

            $wallet->update([
                'pending_balance' => $pendingAfter,
            ]);

            $this->createTransaction(
                wallet: $wallet,
                type: $type,
                amount: $amount,

                availableBefore: $availableBefore,
                availableAfter: $availableBefore,

                pendingBefore: $pendingBefore,
                pendingAfter: $pendingBefore + $amount,

                payment: $payment,
                description: $description,
            );
        });
    }

    public function releasePending(
        Wallet $wallet,
        int $amount,
        WalletTransactionType $type,
        ?Payment $payment = null,
        ?string $description = null,
    ): void
    {
        DB::transaction(function () use (
            $wallet,
            $amount,
            $type,
            $payment,
            $description
        ) {

            $wallet = $this->lockWallet($wallet);

            $availableBefore = $wallet->available_balance;
            $pendingBefore = $wallet->pending_balance;

            if ($pendingBefore < $amount) {
                throw new \RuntimeException('موجودی معلق کافی نیست.');
            }

            $availableAfter = $availableBefore + $amount;
            $pendingAfter = $pendingBefore - $amount;

            $wallet->update([
                'available_balance' => $availableAfter,
                'pending_balance' => $pendingAfter,
            ]);

            $this->createTransaction(
                wallet: $wallet,
                type: $type,
                amount: $amount,
                availableBefore: $availableBefore,
                availableAfter: $availableAfter,
                pendingBefore: $pendingBefore,
                pendingAfter: $pendingAfter,
                payment: $payment,
                description: $description,
            );
        });
    }

    public function creditAvailable(
        Wallet $wallet,
        int $amount,
        WalletTransactionType $type,
        ?Payment $payment = null,
        ?string $description = null,
    ): void
    {
        DB::transaction(function () use (
            $wallet,
            $amount,
            $type,
            $payment,
            $description
        ) {

            $wallet = $this->lockWallet($wallet);

            $availableBefore = $wallet->available_balance;
            $pendingBefore = $wallet->pending_balance;

            $availableAfter = $availableBefore + $amount;

            $wallet->update([
                'available_balance' => $availableAfter,
            ]);

            $this->createTransaction(
                wallet: $wallet,
                type: $type,
                amount: $amount,
                availableBefore: $availableBefore,
                availableAfter: $availableAfter,
                pendingBefore: $pendingBefore,
                pendingAfter: $pendingBefore,
                payment: $payment,
                description: $description,
            );
        });
    }

    public function debitAvailable(
        Wallet $wallet,
        int $amount,
        WalletTransactionType $type,
        ?Payment $payment = null,
        ?string $description = null,
    ): void
    {
        DB::transaction(function () use (
            $wallet,
            $amount,
            $type,
            $payment,
            $description
        ) {

            $wallet = $this->lockWallet($wallet);

            $availableBefore = $wallet->available_balance;
            $pendingBefore = $wallet->pending_balance;

            if ($availableBefore < $amount) {
                throw new \RuntimeException('موجودی قابل برداشت کافی نیست.');
            }

            $availableAfter = $availableBefore - $amount;

            $wallet->update([
                'available_balance' => $availableAfter,
            ]);

            $this->createTransaction(
                wallet: $wallet,
                type: $type,
                amount: $amount,
                availableBefore: $availableBefore,
                availableAfter: $availableAfter,
                pendingBefore: $pendingBefore,
                pendingAfter: $pendingBefore,
                payment: $payment,
                description: $description,
            );
        });
    }

    private function createTransaction(
        Wallet $wallet,
        WalletTransactionType $type,
        int $amount,
        int $availableBefore,
        int $availableAfter,
        int $pendingBefore,
        int $pendingAfter,
        ?Payment $payment = null,
        ?string $description = null,
    ): void
    {
        $wallet->transactions()->create([
            'payment_id' => $payment?->id,

            'type' => $type,

            'amount' => $amount,

            'available_before' => $availableBefore,
            'available_after' => $availableAfter,

            'pending_before' => $pendingBefore,
            'pending_after' => $pendingAfter,

            'description' => $description,
        ]);
    }

    private function lockWallet(Wallet $wallet): Wallet
    {
        return Wallet::query()
            ->lockForUpdate()
            ->findOrFail($wallet->id);
    }

}
