<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum MessageTypes: string implements Contracts\EnumContractInterface
{

    case TEXT = '1';
    case IMAGE = '2';
    case FILE = '3';

    public static function labels(): array
    {
        return [
            self::TEXT->value => 'متن',
            self::IMAGE->value => 'عکس',
            self::FILE->value => 'فایل',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::TEXT->value => 'text',
            self::IMAGE->value => 'image',
            self::FILE->value => 'file',
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
