<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum PlatformTypes: string implements Contracts\EnumContractInterface
{

    case ANDROID = '1';
    case IOS = '2';
    case WINDOWS = '3';
    case UNKNOWN = '0';

    public static function labels(): array
    {
        return [
            self::ANDROID->value => 'اندروید',
            self::IOS->value => 'آی او اس',
            self::WINDOWS->value => 'ویندوز',
            self::UNKNOWN->value => 'ناشناخته',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::ANDROID->value => 'android',
            self::IOS->value => 'ios',
            self::WINDOWS->value => 'windows',
            self::UNKNOWN->value => 'unknown',
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
