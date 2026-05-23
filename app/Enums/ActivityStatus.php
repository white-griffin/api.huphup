<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;
use JetBrains\PhpStorm\Pure;

enum ActivityStatus: string implements EnumContractInterface {
    case ACTIVE = '1';
    case INACTIVE = '2';

    public static function labels(): array {
        return [
            self::ACTIVE->value => 'فعال',
            self::INACTIVE->value => 'غیر فعال',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::ACTIVE->value => 'active',
            self::INACTIVE->value => 'inActive',
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
