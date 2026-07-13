<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum OrderStatuses: string implements Contracts\EnumContractInterface
{

    case PENDING = '1';
    case PAID = '2';
    case CANCELLED = '3';
    case FAILED = '4';
    case COMPLETED = '5';

    public static function labels(): array
    {
        return [
            self::PENDING->value => 'در انتظار',
            self::PAID->value => 'پرداخت شده',
            self::CANCELLED->value => 'لغو شده',
            self::FAILED->value => 'ناموفق',
            self::COMPLETED->value => 'تکمیل شده',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::PENDING->value => 'pending',
            self::PAID->value => 'paid',
            self::CANCELLED->value => 'cancelled',
            self::FAILED->value => 'failed',
            self::COMPLETED->value => 'completed',
        ];
    }

    public static function fromEnglishLabel(string $englishLabel): ?self
    {
        $key = array_search(strtolower($englishLabel), array_map('strtolower', self::englishLabels()));
        return $key !== false ? self::tryFrom($key) : null;
    }
    public static function label(string $value): ?string  {
        return self::labels()[$value] ?? null;
    }

    public static function fromValue(string $value): ?self {
        return self::from($value);
    }

    public static function toKeyValueItems(): array {
        return array_map(
            fn($label, $value) => ['value' => $value, 'label' => $label],
            self::labels(),
            array_keys(self::labels())
        );
    }
}
