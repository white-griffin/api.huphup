<?php

namespace App\Enums\Contracts;

interface EnumContractInterface {
    public static function labels(): array;
    public static function englishLabels(): array;
    public static function label(string $value): ?string;
    public static function fromValue(string $value): ?self;
    public static function toKeyValueItems(): ?array;
}
