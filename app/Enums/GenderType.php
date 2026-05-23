<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;
use JetBrains\PhpStorm\Pure;

enum GenderType: string implements EnumContractInterface {
    case MALE = '1';
    case FEMALE = '2';
    case UNKNOWN = '0';

    public static function labels(): array
    {
        return [
            self::MALE->value => 'مرد',
            self::FEMALE->value => 'زن',
            self::UNKNOWN->value => 'نامشخص',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::MALE->value => 'male',
            self::FEMALE->value => 'female',
            self::UNKNOWN->value => 'unknown',
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
