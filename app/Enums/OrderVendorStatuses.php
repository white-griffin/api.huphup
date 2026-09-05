<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum OrderVendorStatuses: string implements Contracts\EnumContractInterface
{

    case PENDING    = '1';

    case PAID       = '2';
    case PROCESSING = '3';
    case SHIPPED    = '4';
    case COMPLETED  = '5';
    case CANCELED   = '6';
    case FAILED     = '7';

    public static function labels(): array
    {
        return [
            self::PENDING->value   => 'در انتظار',
            self::PAID->value     => 'پرداخت شده',
            self::PROCESSING->value => 'در حال اماده سازی',
            self::SHIPPED->value   => 'ارسال شده',
            self::COMPLETED->value => 'به اتمام رسیده',
            self::CANCELED->value => 'کنسل شده',
            self::FAILED->value   => 'با خطا مواجه شده',
        ];
    }
    public static function englishLabels(): array
    {
        return [
            self::PENDING->value   => 'Pending',
            self::PAID->value     => 'Paid',
            self::PROCESSING->value => 'Processing',
            self::SHIPPED->value   => 'Shipped',
            self::COMPLETED->value => 'Completed',
            self::CANCELED->value => 'Canceled',
            self::FAILED->value   => 'Failed',
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
