<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum ProductAttributeType:string implements Contracts\EnumContractInterface
{

    case COLOR  = '1';
    case SIZE   = '2';
    case WEIGHT = '3';

    public static function labels(): array
    {
        return [
            self::COLOR->value => 'رنگ',
            self::SIZE->value => 'سایز',
            self::WEIGHT->value => 'وزن',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::COLOR->value => 'color',
            self::SIZE->value => 'size',
            self::WEIGHT->value => 'weight',
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
