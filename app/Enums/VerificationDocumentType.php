<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum VerificationDocumentType: string implements Contracts\EnumContractInterface
{
    case NATIONAL_CARD_FRONT = '1';
    case NATIONAL_CARD_BACK = '2';
    case SELFIE = '3';
    case VERIFICATION_VIDEO = '4';

    public static function labels(): array
    {
        return [
            self::NATIONAL_CARD_FRONT->value => 'روی کارت ملی',
            self::NATIONAL_CARD_BACK->value => 'پشت کارت ملی',
            self::SELFIE->value => 'سلفی',
            self::VERIFICATION_VIDEO->value => 'ویدیو احراز هویت',
        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::NATIONAL_CARD_FRONT->value => 'national_card front',
            self::NATIONAL_CARD_BACK->value => 'national_card back',
            self::SELFIE->value => 'selfie',
            self::VERIFICATION_VIDEO->value => 'verification video',
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
