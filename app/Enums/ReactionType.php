<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum ReactionType: string implements EnumContractInterface
{

    case LIKE = '1';
    case DISLIKE = '2';
    case BOOKMARK = '3';
    case HELPFUL = '4';


    public static function labels(): array
    {
        return [
            self::LIKE->value => 'لایک',
            self::DISLIKE->value => 'دیسلایک',
            self::BOOKMARK->value => 'بوکمارک',
            self::HELPFUL->value => 'مفید',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::LIKE->value => 'Like',
            self::DISLIKE->value => 'Dislike',
            self::BOOKMARK->value => 'Bookmark',
            self::HELPFUL->value => 'Helpful',
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
