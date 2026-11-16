<?php

namespace App\Enums\Referral;

use App\Enums\Contracts\EnumContractInterface;

enum ReferralQualificationEvent: string implements EnumContractInterface
{

    case PROFILE_COMPLETED = '1';
    case FIRST_ORDER_COMPLETED = '2';
    case FIRST_PURCHASE = '3';

    public static function labels(): array
    {
        return [
            self::PROFILE_COMPLETED->value => 'کامل کردن پروفایل',
            self::FIRST_ORDER_COMPLETED->value => 'اولین خرید کامل',
            self::FIRST_PURCHASE->value => 'اولین پرداخت',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::PROFILE_COMPLETED->value => 'profile_completed',
            self::FIRST_ORDER_COMPLETED->value => 'first_order_completed',
            self::FIRST_PURCHASE->value => 'first_purchase',
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
