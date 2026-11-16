<?php

namespace App\Services\Xp;

use App\Enums\Xp\XpTransactionType;
use App\Models\User;
use App\Models\UserXpAccount;
use App\Models\UserXpTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use LogicException;

class XpService
{
    /**
     * افزایش XP
     */
    public function earn(
        User $user,
        int $amount,
        string $idempotencyKey,
        ?string $source = null,
        ?int $sourceId = null,
        ?string $description = null
    ): UserXpTransaction {
        return $this->process(
            $user,
            $amount,
            XpTransactionType::EARN,
            $idempotencyKey,
            $source,
            $sourceId,
            $description
        );
    }

    /**
     * مصرف XP برای تبدیل به Token
     */
    public function spendForTokenConversion(
        User $user,
        int $amount,
        string $idempotencyKey,
        ?int $sourceId = null,
        ?string $description = null
    ): UserXpTransaction {
        return $this->process(
            $user,
            $amount,
            XpTransactionType::TOKEN_CONVERSION,
            $idempotencyKey,
            'token_conversion',
            $sourceId,
            $description
        );
    }

    /**
     * پردازش مرکزی تراکنش XP
     */
    private function process(
        User $user,
        int $amount,
        XpTransactionType $type,
        string $idempotencyKey,
        ?string $source = null,
        ?int $sourceId = null,
        ?string $description = null
    ): UserXpTransaction {

        if ($amount <= 0) {
            throw new InvalidArgumentException(
                'XP amount must be greater than zero.'
            );
        }

        return DB::transaction(function () use (
            $user,
            $amount,
            $type,
            $idempotencyKey,
            $source,
            $sourceId,
            $description
        ) {

            // قفل کاربر برای جلوگیری از درخواست‌های هم‌زمان
            $lockedUser = User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            // دریافت یا ایجاد حساب XP
            $account = UserXpAccount::firstOrCreate(
                ['user_id' => $lockedUser->id],
                ['balance' => 0]
            );

            $account = UserXpAccount::query()
                ->whereKey($account->id)
                ->lockForUpdate()
                ->firstOrFail();

            // بررسی Idempotency
            $existingTransaction = UserXpTransaction::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingTransaction) {
                $isSameOperation =
                    $existingTransaction->user_id == $lockedUser->id &&
                    $existingTransaction->amount == $amount &&
                    $existingTransaction->type == $type &&
                    $existingTransaction->source == $source &&
                    $existingTransaction->source_id == $sourceId;

                if (!$isSameOperation) {
                    throw new LogicException(
                        'Idempotency key has already been used for another operation.'
                    );
                }

                return $existingTransaction;
            }

            // بررسی موجودی برای مصرف XP
            if (
                $type == XpTransactionType::TOKEN_CONVERSION &&
                $account->balance < $amount
            ) {
                throw ValidationException::withMessages([
                    'amount' => 'موجودی XP شما کافی نیست.',
                ]);
            }

            // تغییر موجودی
            if ($type == XpTransactionType::EARN) {
                $account->balance += $amount;
            } else {
                $account->balance -= $amount;
            }

            $account->save();

            // ثبت تراکنش
            return UserXpTransaction::create([
                'user_id' => $lockedUser->id,
                'user_xp_account_id' => $account->id,
                'amount' => $amount,
                'type' => $type,
                'source' => $source,
                'source_id' => $sourceId,
                'idempotency_key' => $idempotencyKey,
                'description' => $description,
            ]);
        },3);
    }
}
