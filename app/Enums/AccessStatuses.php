<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum AccessStatuses: string implements Contracts\EnumContractInterface
{
    case PUBLIC = '1';
    case PRIVATE = '2';

    public static function labels(): array
    {
        return [
            self::PUBLIC->value => 'عمومی ',
            self::PRIVATE->value => 'خصوصی ',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::PUBLIC->value => 'public',
            self::PRIVATE->value => 'private',
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
