<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum RoutineCategoryTypes:string implements Contracts\EnumContractInterface
{

    case HEALTH = '1';
    case CARE = '2';
    case NUTRITION = '3';
    case ACTIVITY = '4';
    case MAINTENANCE = '5';

    public static function labels(): array
    {
        return [
            self::HEALTH->value => 'سلامت',
            self::CARE->value => 'مراقبت',
            self::NUTRITION->value => 'تغذیه',
            self::ACTIVITY->value => 'فعالیت',
            self::MAINTENANCE->value => 'نگهداری'
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::HEALTH->value => 'health',
            self::CARE->value => 'care',
            self::NUTRITION->value => 'nutrition',
            self::ACTIVITY->value => 'activity',
            self::MAINTENANCE->value => 'maintenance'
        ];
    }

    public static function fromEnglishLabel(string $englishLabel): ?self
    {
        $key = array_search(strtolower($englishLabel), array_map('strtolower', self::englishLabels()));
        return $key !== false ? self::tryFrom($key) : null;
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
