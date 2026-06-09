<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum DaysOfWeek: string implements Contracts\EnumContractInterface
{

    case SATURDAY = '1';
    case SUNDAY = '2';
    case MONDAY = '3';
    case TUESDAY = '4';
    case WEDNESDAY = '5';
    case THURSDAY = '6';
    case FRIDAY = '7';

    public static function labels(): array
    {
        return [
            self::SATURDAY->value => 'شنبه',
            self::SUNDAY->value => 'یکشنبه',
            self::MONDAY->value => 'دوشنبه',
            self::TUESDAY->value => 'سه شنبه',
            self::WEDNESDAY->value => 'چهارشنبه',
            self::THURSDAY->value => 'پنجشنبه',
            self::FRIDAY->value => 'جمعه',

        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::SATURDAY->value => 'saturday',
            self::SUNDAY->value => 'sunday',
            self::MONDAY->value => 'monday',
            self::TUESDAY->value => 'tuesday',
            self::WEDNESDAY->value => 'wednesday',
            self::THURSDAY->value => 'thursday',
            self::FRIDAY->value => 'friday',

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
