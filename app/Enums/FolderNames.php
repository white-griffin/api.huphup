<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum FolderNames: string implements Contracts\EnumContractInterface
{

    case INBOX = '1';
    case SENT = '2';
    case ARCHIVED = '3';

    public static function labels(): array
    {
        return [
            self::INBOX->value => 'دریافتی',
            self::SENT->value => 'ارسالی',
            self::ARCHIVED->value => 'آرشیو'
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::INBOX->value => 'inbox',
            self::SENT->value => 'sent',
            self::ARCHIVED->value => 'archived'
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
