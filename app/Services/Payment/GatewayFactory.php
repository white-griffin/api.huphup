<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentGateways;
use App\Services\Payment\Gateways\TestGateway;
use http\Exception\InvalidArgumentException;

class GatewayFactory
{
    public static function make(string|int $gateway): PaymentGatewayInterface
    {
        return match ($gateway) {
            PaymentGateways::TEST->value     => app(TestGateway::class),
//            PaymentGateways::ZarinPal => app(ZarinPalGateway::class),
//            PaymentGateways::SnapPay  => app(SnapPayGateway::class),
            default => throw new InvalidArgumentException('درگاه پشتیبانی نمی شود')
        };

        // نکته: اگر gateway نامعتبر باشد، PaymentGateways::label()
        // خودش ValueError پرتاب می‌کند؛ در PaymentService این را می‌گیریم.
    }
}
