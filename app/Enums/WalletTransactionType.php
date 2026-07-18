<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum WalletTransactionType: string implements EnumContractInterface
{

    case PAYMENT = '1';

    case REFUND = '2';

    case RELEASE = '3';

    case WITHDRAW = '4';

    case ADJUSTMENT = '5';

    case DEPOSIT = '6';

    public static function labels(): array
    {
        return [
            self::PAYMENT->value => 'دریافت وجه سفارش',
            self::REFUND->value => 'بازگشت وجه',
            self::RELEASE->value => 'آزادسازی موجودی',
            self::WITHDRAW->value => 'برداشت وجه',
            self::ADJUSTMENT->value => 'اصلاح موجودی',
            self::DEPOSIT->value => 'شارژ کیف پول',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::PAYMENT->value => 'payment',
            self::REFUND->value => 'refund',
            self::RELEASE->value => 'release',
            self::WITHDRAW->value => 'withdraw',
            self::ADJUSTMENT->value => 'adjustment',
            self::DEPOSIT->value => 'deposit',
        ];
    }

    public static function fromEnglishLabel(string $englishLabel): ?self
    {
        $key = array_search(strtolower($englishLabel), array_map('strtolower', self::englishLabels()));
        return $key !== false ? self::tryFrom($key) : null;
    }
    public static function englishLabel(string $value): ?string
    {
        return self::englishLabels()[$value] ?? null;
    }

    public static function label(string $value): ?string
    {
        return self::labels()[$value] ?? null;
    }

    public static function fromValue(string $value): ?self
    {
        return self::from($value);
    }

    public static function toKeyValueItems(): ?array
    {
        return array_map(
            fn($label, $value) => ['value' => $value, 'label' => $label],
            self::labels(),
            array_keys(self::labels())
        );
    }
}
