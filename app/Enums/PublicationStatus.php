<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;
use JetBrains\PhpStorm\Pure;

enum PublicationStatus: string implements EnumContractInterface
{
    case DRAFT = '5';
    case SCHEDULED = '4';
    case PUBLISHED = '1';
    case ARCHIVED = '2';

    case PENDING = '0';
    case REJECTED = '3';

    public static function labels(): array
    {
        return [
            self::DRAFT->value => 'پیش نویس',
            self::SCHEDULED->value => 'در صف',
            self::PUBLISHED->value => 'منتشر شده',
            self::ARCHIVED->value => 'آرشیو شده',
            self::PENDING->value => 'در انتظار',
            self::REJECTED->value => 'رد شده'
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::DRAFT->value => 'draft',
            self::SCHEDULED->value => 'scheduled',
            self::PUBLISHED->value => 'published',
            self::ARCHIVED->value => 'archived',
            self::PENDING->value => 'pending',
            self::REJECTED->value => 'rejected'
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
