<?php

namespace App\Enums;

use App\Enums\Contracts\EnumContractInterface;

enum AppointmentStatuses: string implements Contracts\EnumContractInterface
{

    case PENDING_PAYMENT = '1'; // رزرو ساخته شده، منتظر پرداخت

    case PENDING_CONFIRMATION = '2'; // پرداخت موفق، منتظر تایید پرووایدر

    case CONFIRMED = '3';
    case COMPLETED = '4';
    case CANCELLED = '5';
    case EXPIRED = '6';


    public static function labels(): array
    {
        return [
            self::PENDING_PAYMENT->value => 'در انتظار پرداخت',
            self::PENDING_CONFIRMATION->value => 'در انتظار تایید',
            self::CONFIRMED->value => 'قبول شده',
            self::COMPLETED->value => 'انجام شده',
            self::CANCELLED->value => 'کنسل شده',
            self::EXPIRED->value => 'منقضی شده',

        ];
    }

    public static function englishLabels(): array
    {
        return [
            self::PENDING_PAYMENT->value => 'pending_payment',
            self::PENDING_CONFIRMATION->value => 'pending_confirmation',
            self::CONFIRMED->value => 'confirmed',
            self::COMPLETED->value => 'completed',
            self::CANCELLED->value => 'cancelled',
            self::EXPIRED->value => 'expired',

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
