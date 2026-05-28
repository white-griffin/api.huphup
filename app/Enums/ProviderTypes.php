<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum ProviderTypes: string implements Contracts\EnumContractInterface
{

    case CLINIC = '1';
    case BARBER = '2';
    case SHOPPING = '3';
    case PENSION = '4';


    public static function labels(): array
    {
        return [
            self::CLINIC->value => 'کلینیک دام پزشکی',
            self::BARBER->value => 'ارایشگر',
            self::SHOPPING->value => 'فروشگاه',
            self::PENSION->value => 'پانسیون',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::CLINIC->value => 'clinic',
            self::BARBER->value => 'barber',
            self::SHOPPING->value => 'shopping',
            self::PENSION->value => 'pension',
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
