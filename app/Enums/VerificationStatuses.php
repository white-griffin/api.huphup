<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum VerificationStatuses: string implements Contracts\EnumContractInterface
{

    case PENDING = '0';
    case UNDER_REVIEW = '1';

    case ACTIVE = '2';
    case SUSPENDED = '3';
    case REJECTED = '4';


    public static function labels(): array
    {
        return [
            self::PENDING->value => 'در انتظار ',
            self::UNDER_REVIEW->value => 'در حال بررسی',
            self::ACTIVE->value => 'فعال ',
            self::SUSPENDED->value => 'تعلیق شده',
            self::REJECTED->value => 'رد شده',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::PENDING->value => 'pending',
            self::UNDER_REVIEW->value => 'under_review',
            self::ACTIVE->value => 'active',
            self::SUSPENDED->value => 'suspended',
            self::REJECTED->value => 'rejected',
        ];
    }

    public static function fromEnglishLabel(string $englishLabel): ?self
    {
        $key = array_search(strtolower($englishLabel), array_map('strtolower', self::englishLabels()));
        return $key !== false ? self::tryFrom($key) : null;
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
