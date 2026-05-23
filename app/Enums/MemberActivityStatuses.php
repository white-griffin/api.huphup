<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum MemberActivityStatuses: string implements Contracts\EnumContractInterface
{

    case PENDING = '0';
    case ACTIVE = '1';
    case SUSPENDED = '2';
    case REJECTED = '3';


    public static function labels(): array
    {
        return [
            self::PENDING->value => 'در انتظار ',
            self::ACTIVE->value => 'فعال ',
            self::SUSPENDED->value => 'تعلیق شده',
            self::REJECTED->value => 'رد شده',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::PENDING->value => 'pending',
            self::ACTIVE->value => 'active',
            self::SUSPENDED->value => 'suspended',
            self::REJECTED->value => 'rejected',
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
