<?php

namespace App\Contracts;

use App\Models\Payment;

interface HandlesPayment
{
    public function paymentSucceeded(Payment $payment): void;

    public function paymentFailed(Payment $payment): void;
}
