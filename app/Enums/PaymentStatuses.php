<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum PaymentStatuses: string implements Contracts\EnumContractInterface
{

    case UNPAID = '1';
    case PROCESSING = '2';
    case FAILED = '3';
    case CANCELLED = '4';
    case REFUNDED = '5';
    case EXPIRED = '6';
    case PAID = '7';

    public static function labels(): array
    {
        return [
            self::UNPAID->value => 'پرداخت نشده',
            self::PROCESSING->value => 'در حال پرداخت',
            self::FAILED->value => 'ناموفق',
            self::CANCELLED->value => 'لغو شده',
            self::REFUNDED->value => 'برگشت خورده',
            self::EXPIRED->value => 'منقضی شده',
            self::PAID->value => 'پرداخت شده',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::UNPAID->value => 'unpaid',
            self::PROCESSING->value => 'Processing',
            self::FAILED->value => 'Failed',
            self::CANCELLED->value => 'Cancelled',
            self::REFUNDED->value => 'Refunded',
            self::EXPIRED->value => 'Expired',
            self::PAID->value => 'Paid',
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
