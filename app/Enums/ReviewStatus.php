<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum ReviewStatus: string implements Contracts\EnumContractInterface
{

    case PENDING = '1';
    case APPROVED = '2';
    case REJECTED = '3';

    public static function labels(): array
    {
        return [
            self::PENDING->value => 'در انتظار',
            self::APPROVED->value => 'تایید شده',
            self::REJECTED->value => 'رد شده',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::PENDING->value => 'Pending',
            self::APPROVED->value => 'Approved',
            self::REJECTED->value => 'Rejected',
        ];
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
