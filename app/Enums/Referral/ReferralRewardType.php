<?php

namespace App\Enums\Referral;

use App\Enums\Contracts\EnumContractInterface;

enum ReferralRewardType: string implements EnumContractInterface
{

    case REFERRER = '1';
    case REFERRED = '2';

    public static function labels(): array
    {
        return [
            self::REFERRER->value => 'دعوت کننده',
            self::REFERRED->value => 'دعوت شده',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::REFERRER->value => 'Referrer',
            self::REFERRED->value => 'Referred',
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
