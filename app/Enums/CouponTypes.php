<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum CouponTypes: string implements Contracts\EnumContractInterface
{

    case PERCENTAGE = '1';
    case FIXED = '2';

    public static function labels(): array
    {
        return [
            self::PERCENTAGE->value => 'درصدی',
            self::FIXED->value => 'مقدار'
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::PERCENTAGE->value => 'percentage',
            self::FIXED->value => 'fixed'
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
