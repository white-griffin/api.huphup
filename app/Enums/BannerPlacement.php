<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum BannerPlacement: string implements Contracts\EnumContractInterface
{

    case HOME = '1';
    case SEARCH = '2';
    case CATEGORY = '3';
    case PRODUCT = '4';

    public static function labels(): array
    {
        return [
            self::HOME->value => 'صفحه اصلی',
            self::SEARCH->value => 'صفحه جستجو',
            self::CATEGORY->value => 'صفحه دسته بندی ',
            self::PRODUCT->value => 'صفحه محصولات',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::HOME->value => 'home',
            self::SEARCH->value => 'search',
            self::CATEGORY->value => 'category',
            self::PRODUCT->value => 'product',
        ];
    }

    public static function englishLabel(string $value): ?string
    {
        return self::englishLabels()[$value] ?? null;
    }

    public static function fromEnglishLabel(string $englishLabel): ?self
    {
        $key = array_search(strtolower($englishLabel), array_map('strtolower', self::englishLabels()));
        return $key !== false ? self::tryFrom($key) : null;
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
