<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentGateways;
use App\Exceptions\PaymentGatewayException;
use App\Services\Payment\Gateways\TestGateway;
use http\Exception\InvalidArgumentException;

class GatewayFactory
{
    public static function make(string|int $gateway): PaymentGatewayInterface
    {
        $gatewayEnum = PaymentGateways::fromValue($gateway);

        return match ($gatewayEnum) {
            PaymentGateways::TEST     => app(TestGateway::class),
//            PaymentGateways::ZARINPAL => app(ZarinPalGateway::class),
            default                   => throw new PaymentGatewayException('درگاه پشتیبانی نمی شود')
        };

        // نکته: اگر gateway نامعتبر باشد، PaymentGateways::label()
        // خودش ValueError پرتاب می‌کند؛ در PaymentService این را می‌گیریم.
    }
}
