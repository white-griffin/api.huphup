<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum ChatTypes: string implements Contracts\EnumContractInterface
{

    case DIRECT = 'DIRECT';
    case GROUP = 'GROUP';

    public static function labels(): array
    {
        return [
            self::DIRECT->value => 'مستقیم',
            self::GROUP->value => 'گروه',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::DIRECT->value => 'DIRECT',
            self::GROUP->value => 'GROUP',
        ];
    }

    public static function label(string $value): ?string
    {
        return self::labels()[$value] ?? null;
    }

    public static function fromEnglishLabel(string $englishLabel): ?self
    {
        $key = array_search(strtolower($englishLabel), array_map('strtolower', self::englishLabels()));
        return $key !== false ? self::tryFrom($key) : null;
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
