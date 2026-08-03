<?php

namespace App\Services\Wallet;

use App\Enums\WalletTransactionType;
use App\Exceptions\WalletException;
use App\Models\Payment;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function transfer(
        Wallet $from,
        Wallet $to,
        int $amount,
        WalletTransactionType $debitType,
        WalletTransactionType $creditType,
        ?Payment $payment = null,
        ?string $description = null,
    ): void {
        DB::transaction(function () use (
            $from,
            $to,
            $amount,
            $debitType,
            $creditType,
            $payment,
            $description
        ) {
            $this->validateAmount($amount);

            [$from, $to] = $this->lockPair($from, $to);

            $this->debitAvailableInternal(
                wallet: $from,
                amount: $amount,
                type: $debitType,
                payment: $payment,
                description: $description,
            );

            $this->creditPendingInternal(
                wallet: $to,
                amount: $amount,
                type: $creditType,
                payment: $payment,
                description: $description,
            );
        });
    }

    public function refundPending(
        Wallet $from,
        Wallet $to,
        int $amount,
        WalletTransactionType $debitType,
        WalletTransactionType $creditType,
        ?Payment $payment = null,
        ?string $description = null,
    ): void {
        DB::transaction(function () use (
            $from,
            $to,
            $amount,
            $debitType,
            $creditType,
            $payment,
            $description
        ) {
            $this->validateAmount($amount);

            [$from, $to] = $this->lockPair($from, $to);

            $this->debitPendingInternal(
                wallet: $from,
                amount: $amount,
                type: $debitType,
                payment: $payment,
                description: $description,
            );

            $this->creditAvailableInternal(
                wallet: $to,
                amount: $amount,
                type: $creditType,
                payment: $payment,
                description: $description,
            );
        });
    }

    public function creditPending(
        Wallet $wallet,
        int $amount,
        WalletTransactionType $type,
        ?Payment $payment = null,
        ?string $description = null,
    ): void {
        DB::transaction(function () use (
            $wallet,
            $amount,
            $type,
            $payment,
            $description
        ) {
            $this->validateAmount($amount);

            $wallet = $this->lockWallet($wallet);

            $this->creditPendingInternal(
                wallet: $wallet,
                amount: $amount,
                type: $type,
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
    ): void {
        DB::transaction(function () use (
            $wallet,
            $amount,
            $type,
            $payment,
            $description
        ) {
            $this->validateAmount($amount);

            $wallet = $this->lockWallet($wallet);

            $this->releasePendingInternal(
                wallet: $wallet,
                amount: $amount,
                type: $type,
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
    ): void {
        DB::transaction(function () use (
            $wallet,
            $amount,
            $type,
            $payment,
            $description
        ) {
            $this->validateAmount($amount);

            $wallet = $this->lockWallet($wallet);

            $this->creditAvailableInternal(
                wallet: $wallet,
                amount: $amount,
                type: $type,
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
    ): void {
        DB::transaction(function () use (
            $wallet,
            $amount,
            $type,
            $payment,
            $description
        ) {
            $this->validateAmount($amount);

            $wallet = $this->lockWallet($wallet);

            $this->debitAvailableInternal(
                wallet: $wallet,
                amount: $amount,
                type: $type,
                payment: $payment,
                description: $description,
            );
        });
    }

    public function settlePending(
        Wallet $wallet,
        int $amount,
        int $commissionAmount,
        WalletTransactionType $type,
        ?Payment $payment = null,
        ?string $description = null,
    ): void {
        DB::transaction(function () use (
            $wallet,
            $amount,
            $commissionAmount,
            $type,
            $payment,
            $description
        ) {
            $this->validateAmount($amount);

            if ($commissionAmount < 0 || $commissionAmount > $amount) {
                throw new WalletException(
                    'مبلغ کمیسیون نامعتبر است.'
                );
            }

            $wallet = $this->lockWallet($wallet);

            $availableBefore = $wallet->available_balance;
            $pendingBefore = $wallet->pending_balance;

            if ($pendingBefore < $amount) {
                throw new WalletException(
                    'موجودی معلق کیف پول کافی نیست.'
                );
            }

            $netAmount = $amount - $commissionAmount;

            $availableAfter = $availableBefore + $netAmount;
            $pendingAfter = $pendingBefore - $amount;

            $wallet->update([
                'available_balance' => $availableAfter,
                'pending_balance' => $pendingAfter,
            ]);

            $this->createTransaction(
                wallet: $wallet,
                type: $type,
                amount: $netAmount,
                availableBefore: $availableBefore,
                availableAfter: $availableAfter,
                pendingBefore: $pendingBefore,
                pendingAfter: $pendingAfter,
                payment: $payment,
                description: $description,
            );
        });
    }

    private function creditPendingInternal(
        Wallet $wallet,
        int $amount,
        WalletTransactionType $type,
        ?Payment $payment = null,
        ?string $description = null,
    ): void {
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
            pendingAfter: $pendingAfter,
            payment: $payment,
            description: $description,
        );
    }

    private function releasePendingInternal(
        Wallet $wallet,
        int $amount,
        WalletTransactionType $type,
        ?Payment $payment = null,
        ?string $description = null,
    ): void {
        $availableBefore = $wallet->available_balance;
        $pendingBefore = $wallet->pending_balance;

        if ($pendingBefore < $amount) {
            throw new WalletException(
                'موجودی معلق کیف پول کافی نیست.'
            );
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
    }

    private function creditAvailableInternal(
        Wallet $wallet,
        int $amount,
        WalletTransactionType $type,
        ?Payment $payment = null,
        ?string $description = null,
    ): void {
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
    }

    private function debitAvailableInternal(
        Wallet $wallet,
        int $amount,
        WalletTransactionType $type,
        ?Payment $payment = null,
        ?string $description = null,
    ): void {
        $availableBefore = $wallet->available_balance;
        $pendingBefore = $wallet->pending_balance;

        if ($availableBefore < $amount) {
            throw new WalletException(
                'موجودی کیف پول کافی نیست.'
            );
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
    }

    private function debitPendingInternal(
        Wallet $wallet,
        int $amount,
        WalletTransactionType $type,
        ?Payment $payment = null,
        ?string $description = null,
    ): void {
        $availableBefore = $wallet->available_balance;
        $pendingBefore = $wallet->pending_balance;

        if ($pendingBefore < $amount) {
            throw new WalletException(
                'موجودی معلق کیف پول کافی نیست.'
            );
        }

        $pendingAfter = $pendingBefore - $amount;

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
            pendingAfter: $pendingAfter,
            payment: $payment,
            description: $description,
        );
    }

    private function lockWallet(Wallet $wallet): Wallet
    {
        return Wallet::query()
            ->lockForUpdate()
            ->findOrFail($wallet->id);
    }

    private function lockPair(
        Wallet $from,
        Wallet $to
    ): array {
        if ($from->id === $to->id) {
            throw new WalletException(
                'مبدأ و مقصد تراکنش نمی‌توانند یک کیف پول باشند.'
            );
        }

        if ($from->id < $to->id) {
            $first = $this->lockWallet($from);
            $second = $this->lockWallet($to);
        } else {
            $first = $this->lockWallet($to);
            $second = $this->lockWallet($from);
        }

        $lockedFrom = $first->id === $from->id
            ? $first
            : $second;

        $lockedTo = $first->id === $to->id
            ? $first
            : $second;

        return [$lockedFrom, $lockedTo];
    }

    private function validateAmount(int $amount): void
    {
        if ($amount <= 0) {
            throw new WalletException(
                'مبلغ تراکنش باید بیشتر از صفر باشد.'
            );
        }
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
    ): void {
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
}
