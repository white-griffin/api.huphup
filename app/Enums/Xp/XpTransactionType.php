<?php

namespace App\Enums\Xp;

use App\Enums\Contracts\EnumContractInterface;

enum XpTransactionType: string implements EnumContractInterface
{

    case EARN = '1';
    case TOKEN_CONVERSION = '2';

    public static function labels(): array
    {
        return [
            self::EARN->value => 'کسب کردن',
            self::TOKEN_CONVERSION->value => 'تبدیل به توکن',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::EARN->value => 'earn',
            self::TOKEN_CONVERSION->value => 'token_conversion',
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
