<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum AppointmentStatuses: string implements Contracts\EnumContractInterface
{

    case PENDING = '1';
    case CONFIRMED = '2';
    case CANCELLED = '3';
    case COMPLETED = '4';

    public static function labels(): array
    {
        return [
            self::PENDING->value => 'در انتظار',
            self::CONFIRMED->value => 'قبول شده',
            self::CANCELLED->value => 'کنسل شده',
            self::COMPLETED->value => 'انجام شده',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::PENDING->value => 'pending',
            self::CONFIRMED->value => 'confirmed',
            self::CANCELLED->value => 'cancelled',
            self::COMPLETED->value => 'completed',
        ];
    }

    public static function label(string $value): ?string  {
        return self::labels()[$value] ?? null;
    }

    public static function fromValue(string $value): ?self {
        return self::from($value);
    }

    public static function toKeyValueItems(): array {
        return array_map(
            fn($label, $value) => ['value' => $value, 'label' => $label],
            self::labels(),
            array_keys(self::labels())
        );
    }
}
