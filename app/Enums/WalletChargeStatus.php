<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum WalletChargeStatus: string implements Contracts\EnumContractInterface
{
    case PENDING = '1';
    case PAID = '2';
    case FAILED = '3';

    public static function labels(): array
    {
        // TODO: Implement labels() method.
    }

    public static function englishLabels(): array
    {
        // TODO: Implement englishLabels() method.
    }

    public static function label(string $value): ?string
    {
        // TODO: Implement label() method.
    }

    public static function fromEnglishLabel(string $englishLabel): ?self
    {
        // TODO: Implement fromEnglishLabel() method.
    }

    public static function fromValue(string $value): ?self
    {
        // TODO: Implement fromValue() method.
    }

    public static function toKeyValueItems(): ?array
    {
        // TODO: Implement toKeyValueItems() method.
    }
}
