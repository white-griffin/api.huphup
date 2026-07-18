<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum OrderStatuses: string implements Contracts\EnumContractInterface
{

    case PENDING   = '1';
    case PAID      = '2';
    case PROCESSING = '3';
    case SHIPPED   = '4';
    case COMPLETED = '5';
    case CANCELED  = '6';
    case FAILED    = '7';

    public static function labels(): array
    {
        return [
            self::PENDING->value => 'در انتظار',
            self::PAID->value => 'پرداخت شده',
            self::PROCESSING->value => 'درحال آماده سازی',
            self::SHIPPED->value => 'ارسال شده',
            self::CANCELED->value => 'لغو شده',
            self::FAILED->value => 'ناموفق',
            self::COMPLETED->value => 'تکمیل شده',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::PENDING->value => 'pending',
            self::PAID->value => 'paid',
            self::PROCESSING->value => 'processing',
            self::SHIPPED->value => 'shipped',
            self::CANCELED->value => 'cancelled',
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
