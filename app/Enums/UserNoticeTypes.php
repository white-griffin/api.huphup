<?php

namespace App\Enums;


use App\Enums\Contracts\EnumContractInterface;

enum UserNoticeTypes: string implements EnumContractInterface
{

    case GENERAL = '1';
    case PRIVATE = '2';
    case ORDER = '3';
    case PAYMENT = '4';
    case SYSTEM = '5';

    public static function labels(): array
    {
        return [
            self::GENERAL->value => 'عمومی ',
            self::PRIVATE->value => 'خصوصی ',
            self::ORDER->value => 'سفارش',
            self::PAYMENT->value => 'پرداخت ',
            self::SYSTEM->value => 'سیستم ',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::GENERAL->value => 'general',
            self::PRIVATE->value => 'private',
            self::ORDER->value => 'order',
            self::PAYMENT->value => 'payment',
            self::SYSTEM->value => 'system',
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
