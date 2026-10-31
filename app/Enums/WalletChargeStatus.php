<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum WalletChargeStatus: string implements Contracts\EnumContractInterface
{
    case PENDING = '1';
    case PAID = '2';
    case FAILED = '3';

    public static function labels(): array
    {
        return [
            self::PENDING->value => 'در انتظار',
            self::PAID->value => 'شارژ شده',
            self::FAILED->value => 'خطا در شارژ',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::PENDING->value => 'Pending',
            self::PAID->value => 'Paid',
            self::FAILED->value => 'Failed',
        ];
    }

    public static function englishLabel(string $value): ?string
    {
        return self::englishLabels()[$value] ?? null;
    }
    public static function fromEnglishLabel(string $englishLabel): ?self
    {
        $key = array_search(strtolower($englishLabel), array_map('strtolower', self::englishLabels()));
        return $key !== false ? self::tryFrom($key) : null;
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
