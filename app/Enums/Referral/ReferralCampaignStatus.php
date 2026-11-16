<?php

namespace App\Enums\Referral;

use App\Enums\Contracts\EnumContractInterface;

enum ReferralCampaignStatus: string implements EnumContractInterface
{

    case DRAFT = '1';
    case ACTIVE = '2';
    case PAUSED = '3';
    case ENDED = '4';

    public static function labels(): array
    {
        return [
            self::DRAFT->value => 'بایگانی',
            self::ACTIVE->value => 'فعال',
            self::PAUSED->value => 'متوقف شده',
            self::ENDED->value => 'پایان یافته',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::DRAFT->value => 'draft',
            self::ACTIVE->value => 'active',
            self::PAUSED->value => 'paused',
            self::ENDED->value => 'ended',
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
