<?php

namespace App\Services\Sms;

use App\Contracts\SmsProvider;
use App\Enums\SmsProviders;
use App\Services\Sms\Providers\SmsIrProvider;
use http\Exception\InvalidArgumentException;

class SmsProviderFactory
{
    public static function make(SmsProviders $provider): SmsProvider
    {
        return match ($provider) {

            SmsProviders::SMS_IR => app(SmsIrProvider::class),

            default => throw new InvalidArgumentException(
                "Unsupported sms provider [$provider->value]"
            ),
        };
    }
}
