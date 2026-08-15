<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum OrderItemStatuses: string implements EnumContractInterface
{

    case PENDING = '1';
    case PROCESSING = '2';
    case COMPLETED = '3';
    case CANCELED = '4';

    public static function labels(): array
    {
        return [
            self::PENDING->value => 'در انتظار',
            self::PROCESSING->value => 'درحال آماده سازی',
            self::CANCELED->value => 'لغو شده',
            self::COMPLETED->value => 'تکمیل شده',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::PENDING->value => 'pending',
            self::PROCESSING->value => 'processing',
            self::CANCELED->value => 'cancelled',
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
