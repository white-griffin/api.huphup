<?php

namespace App\Services\Sms\Providers;

use App\Contracts\SmsProvider;

class SmsIrProvider implements SmsProvider
{

    public function send(string $to, string $message): bool
    {
        // TODO: Implement send() method.
        return true;
    }
}
