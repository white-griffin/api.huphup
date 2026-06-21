<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum RoutineStatuses: string implements Contracts\EnumContractInterface
{

    case UPCOMING = '1';
    case DUE_SOON = '2';
    case DUE_TODAY = '3';
    case OVERDUE = '4';
    case PAUSED = '5';
    case ARCHIVED = '6';

    public static function labels(): array
    {
        return [
            self::UPCOMING->value => 'در آینده',
            self::DUE_SOON->value => 'به زودی',
            self::DUE_TODAY->value => 'موعد امروز',
            self::OVERDUE->value => 'معوقه',
            self::PAUSED->value => 'متوقف',
            self::ARCHIVED->value => 'بایگانی شده'
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::UPCOMING->value => 'upcoming',
            self::DUE_SOON->value => 'due soon',
            self::DUE_TODAY->value => 'due today',
            self::OVERDUE->value => 'overdue',
            self::PAUSED->value => 'paused',
            self::ARCHIVED->value => 'archived'
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
