<?php

namespace App\Enums;


use App\Enums\Contracts\EnumContractInterface;

enum LogisticsPaymentStatuses: string implements EnumContractInterface
{

    case PENDING = '1';
    case PAID = '2';
    case CANCELED = '3';

    public static function labels(): array
    {
        return [
            self::PENDING->value => 'در انتظار',
            self::PAID->value => 'پرداخت شده',
            self::CANCELED->value => 'لغو شده',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::PENDING->value => 'pending',
            self::PAID->value => 'paid',
            self::CANCELED->value => 'canceled',
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
