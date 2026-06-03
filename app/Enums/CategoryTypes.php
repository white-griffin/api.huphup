<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum CategoryTypes: string implements Contracts\EnumContractInterface
{

    case PRODUCT = '1';
    case SERVICE = '2';
    case BLOG = '3';

    public static function labels(): array
    {
        return [
            self::PRODUCT->value => 'محصولات',
            self::SERVICE->value => 'سرویس ها',
            self::BLOG->value => 'بلاگ'
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::PRODUCT->value => 'product',
            self::SERVICE->value => 'service',
            self::BLOG->value => 'blog'
        ];
    }

    public static function englishLabel(string $value): ?string
    {
        return self::englishLabels()[$value] ?? null;
    }

    public static function label(string $value): ?string
    {
        return self::labels()[$value] ?? null;
    }

    public static function fromValue(string $value): ?self
    {
        return self::from($value);
    }

    public static function toKeyValueItems(): ?array
    {
        return array_map(
            fn($label, $value) => ['value' => $value, 'label' => $label],
            self::labels(),
            array_keys(self::labels())
        );
    }
}
