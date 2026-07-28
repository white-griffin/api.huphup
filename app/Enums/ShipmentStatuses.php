<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum ShipmentStatuses: string implements Contracts\EnumContractInterface
{

    case PENDING = '1';
    case ACCEPTED = '2';
    case PICKED = '3';
    case DELIVERING = '4';
    case DELIVERED = '5';
    case FAILED = '6';
    case CANCELLED = '7';

    public static function labels(): array
    {
        return [
            self::PENDING->value => 'در انتظار',
            self::ACCEPTED->value => 'قبول شده',
            self::PICKED->value => 'دریافت شده',
            self::DELIVERING->value => 'در حال ارسال',
            self::DELIVERED->value => 'تحویل شده',
            self::FAILED->value => 'نا موفق',
            self::CANCELLED->value => 'لغو شده',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::PENDING->value => 'Pending',
            self::ACCEPTED->value => 'Accepted',
            self::PICKED->value => 'Picked',
            self::DELIVERING->value => 'Delivered',
            self::DELIVERED->value => 'Delivered',
            self::FAILED->value => 'Failed',
            self::CANCELLED->value => 'Cancelled',
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
