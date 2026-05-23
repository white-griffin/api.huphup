<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum Priorities: string implements Contracts\EnumContractInterface
{
    case LOW = '1';
    case NORMAL = '2';
    case NECESSARY = '3';

    public static function labels(): array
    {
        return [
            self::LOW->value => 'پایین',
            self::NORMAL->value => 'معمولی',
            self::NECESSARY->value => 'ضروری'
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::LOW->value => 'low',
            self::NORMAL->value => 'normal',
            self::NECESSARY->value => 'necessary'
        ];
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
