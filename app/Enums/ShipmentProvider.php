<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum ShipmentProvider: string implements Contracts\EnumContractInterface
{

    case SANDBOX = '1';
    case ALOPEYK = '2';
    case SNAPP = '3';
    case TIPAX = '4';
    public static function labels(): array
    {
        return [
            self::SANDBOX->value => 'درایور تست',
            self::ALOPEYK->value => 'الو پیک',
            self::SNAPP->value => 'اسنپ',
            self::TIPAX->value => 'تیپاکس',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::SANDBOX->value => 'sandbox',
            self::ALOPEYK->value => 'alopeyk',
            self::SNAPP->value => 'snapp',
            self::TIPAX->value => 'tipax',
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
