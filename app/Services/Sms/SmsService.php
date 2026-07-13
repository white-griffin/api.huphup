<?php

namespace App\Services\Sms;

use App\Contracts\SmsProvider;
use App\Enums\SmsProviders;

class SmsService
{
    protected SmsProvider $provider;

    public function provider(SmsProviders $provider): self
    {
        $this->provider = SmsProviderFactory::make($provider);

        return $this;
    }

    public function send(string $to, string $message): bool
    {
        return $this->provider->send($to, $message);
    }
}
